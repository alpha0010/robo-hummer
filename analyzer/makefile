# Config.

SRCS := $(wildcard src/*.cpp)
OUT = analyzer

OBJDIR = obj
OUTDIR = bin

CXX = g++
LD = g++

MARSYAS_INSTALL = /home2/mng-local/dev/marsyas

INC = -I${MARSYAS_INSTALL}/include -I/usr/include/evhtp
CFLAGS = -Wall -Wextra -std=c++11 -O2
LIB = -lmarsyas -levent -levhtp
LDFLAGS = -s -Wl,--no-undefined -Wl,-rpath=${MARSYAS_INSTALL}/lib \
          -L${MARSYAS_INSTALL}/lib


# Rules.

OBJS = $(SRCS:%.cpp=$(OBJDIR)/%.o)

all: output_dirs $(OUTDIR)/$(OUT) $(OUTDIR)/data $(OUTDIR)/recordings

help:
	@echo "Targets:"
	@echo "  all     - build the executable"
	@echo "  clean   - clear objects and binaries"
	@echo "  help    - show this message\n"

clean:
	rm -f $(OBJS) $(OUTDIR)/$(OUT) $(OUTDIR)/data $(OUTDIR)/recordings

output_dirs:
	@test -d $(OUTDIR) || { mkdir -p $(OUTDIR); echo mkdir $(OUTDIR); }
	@test -d $(OBJDIR)/src || { mkdir -p $(OBJDIR)/src; echo mkdir $(OBJDIR); }

$(OUTDIR)/data:
	ln -s `pwd`/../data $(OUTDIR)/data

$(OUTDIR)/recordings:
	ln -s `pwd`/../web/storage/app/recordings $(OUTDIR)/recordings

$(OUTDIR)/$(OUT): $(OBJS)
	$(LD) -fPIC -o $(OUTDIR)/$(OUT) $(OBJS) $(LDFLAGS) $(LIB)

$(OBJDIR)/%.o: %.cpp
	$(CXX) -MMD -MP -fPIC $(CFLAGS) $(INC) -c $< -o $@

-include $(SRCS:%.cpp=$(OBJDIR)/%.d)

.PHONY: all help clean output_dirs
